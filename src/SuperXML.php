<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\SuperXMLInterface;
use SimpleXMLElement;

/**
 * node 选取node元素的所有子节点（必须是第一个节点）
 * / 表示从XML文件中的根节点开始解析
 * // 表示在XML文件中匹配已选择的当前节点，且不考虑其位置关系（类似于SQL中模糊查询）
 * . 表示选取当前节点
 * .. 表示选取当前节点的父节点
 * @ 表示匹配具体的节点或属性 eg: //node[@lang='lang']
 * * 匹配任何元素节点 eg: /node/*
 */
class SuperXML implements SuperXMLInterface
{

    /**
     * @var string
     */
    protected string $content;

    /**
     * @var array
     */
    protected array $replaces = [];

    /**
     * @var SimpleXMLElement|null
     */
    protected ?SimpleXMLElement $simpleXMLElement;

    /**
     * 实例化
     *
     * @param string $content
     * @param array $replaces
     * @return static
     */
    public static function instance(string $content, array $replaces = []): static
    {
        $xml = new static();
        $xml->setContent($content);
        $xml->setReplaces($replaces);

        return $xml->transform();
    }

    /**
     * @return $this
     */
    public function transform(): static
    {
        $content = $this->content;
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        $xml = simplexml_load_string($content);

        $xml && $this->simpleXMLElement = $xml;

        return $this;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    public static function toArray(SimpleXMLElement $xml): array
    {
        return json_decode(json_encode($xml), true);
    }

    /**
     * 根据路径获取数据
     *
     * @param string $path
     * @param string $default
     * @return string
     */
    public function findValue(string $path, string $default = ''): string
    {
        $element = $this->findElement($path);

        return $element ? strval($element) : $default;
    }

    /**
     * 获取某路径下第一个元素
     *
     * @param string $path
     * @param int $index
     * @return SimpleXMLElement|null
     */
    public function findElement(string $path, int $index = 0): SimpleXMLElement|null
    {
        $elements = $this->findElements($path);

        return $elements[$index] ?? null;
    }

    /**
     * 获取某路径下所有元素
     *
     * @param string $path (relative path to root)
     * @return SimpleXMLElement[]
     */
    public function findElements(string $path): array
    {
        return $this->simpleXMLElement->xpath($path);
    }

    /**
     * 获取XML属性值
     *
     * @param string $attr
     * @param null $default
     * @return mixed
     */
    public function findAttr(string $attr, mixed $default = null): mixed
    {
        if (isset($this->simpleXMLElement[$attr])) {
            return strval($this->simpleXMLElement[$attr]);
        }

        return $default;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param array $replaces
     * @return $this
     */
    public function setReplaces(array $replaces): static
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * @param SimpleXMLElement $simpleXMLElement
     * @return $this
     */
    public function setSimpleXMLElement(SimpleXMLElement $simpleXMLElement): static
    {
        $contents = $simpleXMLElement->asXML();

        return $this->setContent($contents)->transform();
    }

}
